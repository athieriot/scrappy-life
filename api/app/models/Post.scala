package models

import java.time.Instant

case class Post(content: String, author: Option[String], date: Option[Instant])

object Post{
  import play.api.libs.json._

  implicit val postFormat: OFormat[Post] = Json.format[Post]
}