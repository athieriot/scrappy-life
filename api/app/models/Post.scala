package models

import java.time.Instant
import javax.inject.Inject

import play.modules.reactivemongo.ReactiveMongoApi
import reactivemongo.api.Cursor.FailOnError
import reactivemongo.api.ReadPreference.primary
import reactivemongo.api.commands.WriteResult
import reactivemongo.bson.{BSONDateTime, BSONDocument, BSONString}
import reactivemongo.play.json._
import reactivemongo.play.json.collection.JSONCollection

import scala.concurrent.{ExecutionContext, Future}

//TODO: Has to display the Mongo ISODate as String
case class Post(_id: String, content: Option[String], author: Option[String], date: Option[String])

case class PostResponse(post: Post)
case class PostsResponse(posts: Seq[Post], count: Int)

object Post {
  import play.api.libs.json._
  implicit val postFormat: OFormat[Post] = Json.format[Post]
}

object PostResponse {
  import play.api.libs.json._
  implicit val postResponseFormat: OFormat[PostResponse] = Json.format[PostResponse]
}

object PostsResponse {
  import play.api.libs.json._
  implicit val postsResponseFormat: OFormat[PostsResponse] = Json.format[PostsResponse]
}

class PostRepository @Inject()(implicit ec: ExecutionContext, reactiveMongoApi: ReactiveMongoApi) {

  val POST_COLLECTION = "posts"

  def postsCollection: Future[JSONCollection] = reactiveMongoApi.database.map(_.collection(POST_COLLECTION))

  //TODO: Not particularly fond of that signature
  def find(author: Option[String], from: Option[Instant], to: Option[Instant]): Future[Seq[Post]] = {
    import Criteria._

    val criterion = List(
      Criteria("author", "$text", author.map(BSONString)),
      Criteria("timestamp", "$gte", from.map(_.toEpochMilli).map(BSONDateTime)),
      Criteria("timestamp", "$lte", to.map(_.toEpochMilli).map(BSONDateTime))
    )

    postsCollection.flatMap(_.find(toQuery(criterion))
      .cursor[Post](primary)
      .collect[Seq](-1, FailOnError[Seq[Post]]())
    )
  }

  def getOne(id: String): Future[Option[Post]] = {
    val query = BSONDocument("_id" -> id)
    postsCollection.flatMap(_.find(query).one[Post])
  }

  def add(post: Post): Future[WriteResult] = {
    postsCollection.flatMap(_.insert(post))
  }
}