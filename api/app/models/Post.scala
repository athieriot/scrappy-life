package models

import javax.inject.Inject

import play.api.libs.json.Json
import play.modules.reactivemongo.ReactiveMongoApi
import reactivemongo.api.Cursor.FailOnError
import reactivemongo.api.ReadPreference.primary
import reactivemongo.api.commands.WriteResult
import reactivemongo.bson.{BSONDocument, BSONObjectID}
import reactivemongo.play.json._
import reactivemongo.play.json.collection.JSONCollection

import scala.concurrent.{ExecutionContext, Future}

case class Post(_id: Option[String], content: Option[String], author: Option[String], date: Option[String])

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

  def getAll: Future[Seq[Post]] = {
    val query = Json.obj()
    postsCollection.flatMap(_.find(query)
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