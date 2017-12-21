package models

import java.time.Instant
import javax.inject.Inject

import play.modules.reactivemongo.ReactiveMongoApi
import reactivemongo.api.Cursor.FailOnError
import reactivemongo.api.ReadPreference.primary
import reactivemongo.api.commands.WriteResult
import reactivemongo.bson.{BSONDateTime, BSONDocument, BSONElementSet, BSONString, BSONValue}
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

  case class Criteria(field: String, matcher: String, value: Option[BSONValue])

  def getAll(author: Option[String], from: Option[Instant]): Future[Seq[Post]] = {
    val criterion = List(
      Criteria("author", "$text", author.map(BSONString)),
      Criteria("from", "$text", from.map(i => BSONDateTime(i.toEpochMilli)))
    )

    postsCollection.flatMap(_.find(toQuery(criterion))
      .cursor[Post](primary)
      .collect[Seq](-1, FailOnError[Seq[Post]]())
    )
  }

  private def toQuery(criterion: List[Criteria]): BSONDocument = {
    val stuff = criterion.flatMap { c =>
      c.value match {
        case None => None
        case Some(_) => c.matcher match {
          case "$text" => Some(BSONDocument("$text" -> BSONDocument("$search" -> c.value)))
          case _ => Some(BSONDocument(c.field -> BSONDocument(c.matcher -> c.value)))
        }
      }
    }

    if (stuff.isEmpty) BSONDocument() else BSONDocument("$and" -> stuff)
  }

  def getOne(id: String): Future[Option[Post]] = {
    val query = BSONDocument("_id" -> id)
    postsCollection.flatMap(_.find(query).one[Post])
  }

  def add(post: Post): Future[WriteResult] = {
    postsCollection.flatMap(_.insert(post))
  }
}